import csv

assignmentList = []
quizList = []
choiceList = []
forumList = []

#initialize all lists of data
with open('data.csv', 'r') as dataFile:
    data = csv.reader(dataFile)
    #build a list of each module type
    for row in data:
        if row[3] == 'Assignment':
            assignmentList.append(row[0:6])
        elif row[3] == 'Quiz':
            quizList.append(row[0:8])
        elif row[3] == 'Choice':
            choiceList.append(row[0:5])
        elif row[3] == 'Forum':
            forumList.append(row[0:5]+row[8:11])

#get data on assignments
totalSum = 0
zeroDays = 0
oneDay = 0
twoDays = 0
threeDays = 0
fourDays = 0
fiveDays = 0
late = 0
assignmentCount = 0

for assignment in assignmentList:
    daysEarly = int(assignment[5])
    if abs(daysEarly) <=7:
        assignmentCount += 1
        #get info on number days assignments submitted early
        if daysEarly < 0:
            late += 1
        else:
            totalSum += daysEarly
            if daysEarly == 0:
                zeroDays += 1
            elif daysEarly == 1:
                oneDay += 1
            elif daysEarly == 2:
                twoDays += 1
            elif daysEarly == 3:
                threeDays += 1
            elif daysEarly == 4:
                fourDays += 1
            else:
                fiveDays += 1
average = totalSum/assignmentCount
print("ASSIGNMENT DATA")
print("Average days early for assignments:",round(average,2),"days")
print('Assignments submitted 0 days early:',str(round(zeroDays/assignmentCount*100,2))+"%")
print('Assignments submitted 1 day early:', str(round(oneDay/assignmentCount*100,2))+"%")
print('Assignments submitted 2 days early:',str(round(twoDays/assignmentCount*100,2))+"%")
print('Assignments submitted 3 days early:',str(round(threeDays/assignmentCount*100,2))+"%")
print('Assignments submitted 4 days early:',str(round(fourDays/assignmentCount*100,2))+"%")
print('Assignments submitted 5 or more days early:',str(round(fiveDays/assignmentCount*100,2))+"%")
print('Assignments submitted late:',str(round(late/assignmentCount*100,2))+"%")

#get data on quizzes
totalSum = 0
zeroDays = 0
oneDay = 0
twoDays = 0
threeDays = 0
fourDays = 0
fiveDays = 0
late = 0

totalAttempts = 0
zeroAttempts = 0
oneAttempt = 0
twoAttempts = 0
threeAttempts = 0
fourAttempts = 0
quizCount = 0

noTimeSpaced = 0
smallBreakSpaced = 0
largeBreakSpaced = 0
oneDaySpaced = 0
totalDaysSpaced = 0

for quiz in quizList:
    if quiz[5] != '':
        quizCount += 1
        daysEarly = int(quiz[5])
        attempts = int(quiz[6])
        daysSpaced = float(quiz[7])

        #get info on number of days quizzes submitted early
        if daysEarly < 0:
            late += 1
        else:
            totalSum += daysEarly
            totalAttempts += attempts
            totalDaysSpaced += daysSpaced
            if daysEarly == 0:
                zeroDays += 1
            elif daysEarly == 1:
                oneDay += 1
            elif daysEarly == 2:
                twoDays += 1
            elif daysEarly == 3:
                threeDays += 1
            elif daysEarly == 4:
                fourDays += 1
            else:
                fiveDays += 1

        #get info on number of quiz attempts
        if attempts == 0:
            zeroAttempts += 1
        elif attempts == 1:
            oneAttempt += 1
        elif attempts == 2:
            twoAttempts += 1
        elif attempts == 3:
            threeAttempts += 1
        else:
            fourAttempts += 1

        #get info on days spaced between quizzes
        if daysSpaced < .02:
            noTimeSpaced += 1
        elif daysSpaced >= .02 and daysSpaced < .5:
            smallBreakSpaced += 1
        elif daysSpaced >= .5 and daysSpaced < 1:
            largeBreakSpaced += 1
        elif daysSpaced >= 1:
            oneDaySpaced += 1


print("\n\n")
print("QUIZ DATA")
print("Average days early for quizzes:",round(totalSum/quizCount,2),"days")
print('Quizzes submitted 0 days early:',str(round(zeroDays/quizCount*100,2))+"%")
print('Quizzes submitted 1 day early:', str(round(oneDay/quizCount*100,2))+"%")
print('Quizzes submitted 2 days early:',str(round(twoDays/quizCount*100,2))+"%")
print('Quizzes submitted 3 days early:',str(round(threeDays/quizCount*100,2))+"%")
print('Quizzes submitted 4 days early:',str(round(fourDays/quizCount*100,2))+"%")
print('Quizzes submitted 5 or more days early:',str(round(fiveDays/quizCount*100,2))+"%")
print('Quizzes submitted late:',str(round(late/quizCount*100,2))+"%")
print("")
print("Average quiz attempts:",round(totalAttempts/quizCount,2),"attempts")
print('Quizzes attempted 0 times:',str(round(zeroAttempts/quizCount*100,2))+"%")
print('Quizzes attempted 1 time:', str(round(oneAttempt/quizCount*100,2))+"%")
print('Quizzes attempted 2 times:',str(round(twoAttempts/quizCount*100,2))+"%")
print('Quizzes attempted 3 times:',str(round(threeAttempts/quizCount*100,2))+"%")
print('Quizzes attempted 4 times:',str(round(fourAttempts/quizCount*100,2))+"%")
print("")
print("Average quiz spacing:",round(totalDaysSpaced/quizCount,2),"days")
print('Quizzes not spaced out:',str(round(noTimeSpaced/quizCount*100,2))+"%")
print('Quizzes spaced out 1 hour:', str(round(smallBreakSpaced/quizCount*100,2))+"%")
print('Quizzes spaced out half day:',str(round(largeBreakSpaced/quizCount*100,2))+"%")
print('Quizzes spaced out 1 or more days:',str(round(oneDaySpaced/quizCount*100,2))+"%")

#Forum data
postCount = 0
responseCount = 0
for forum in forumList:
    if int(forum[7]) == 1:
        responseCount += 1
    else:
        postCount +=1

print("\n\n")
print("FORUM DATA")
print(postCount+responseCount, "Student Forum Contributions")
print(postCount, "Student Posts")
print(responseCount, "Student Responses")